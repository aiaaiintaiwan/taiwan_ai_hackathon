#!/usr/bin/env python
# coding: utf-8
from __future__ import print_function
import sys
import os
import cv2
import numpy as np
#import matplotlib.pyplot as plt
import shutil
from efficientnet.keras import EfficientNetB5, preprocess_input
from keras.models import Model, load_model
from keras.layers.core import Lambda
#from tensorflow.python.framework import ops
import keras.backend as K
import tensorflow as tf



def target_category_loss(x, category_index, nb_classes):
    return tf.multiply(x, K.one_hot([category_index], nb_classes))

def target_category_loss_output_shape(input_shape):
    return input_shape

def normalize(x):
    return x / (K.sqrt(K.mean(K.square(x))) + 1e-5)

def _compute_gradients(tensor, var_list):
    grads = tf.gradients(tensor, var_list)
    return [grad if grad is not None else tf.zeros_like(var)for var, grad in zip(var_list, grads)]

def grad_cam(input_model, image, category_index, layer_name):
    nb_classes = 2
    target_layer = lambda x: target_category_loss(x, category_index, nb_classes)
    x = Lambda(target_layer, output_shape = target_category_loss_output_shape)(input_model.output)
    
    cam_model = Model(inputs=input_model.input, outputs=x)

    loss = K.sum(cam_model.layers[-1].output)
    
    conv_output =  [l for l in cam_model.layers if l.name == layer_name][0].output
    
    
    grads = normalize(_compute_gradients(loss, [conv_output])[0])
    gradient_function = K.function([cam_model.input], [conv_output, grads])
    
    output, grads_val = gradient_function([image])
    output, grads_val = output[0, :], grads_val[0, :, :, :]


    weights = np.mean(grads_val, axis = (0, 1))
    cam = np.ones(output.shape[0 : 2], dtype = np.float32)

    for i, w in enumerate(weights):
        cam += w * output[:, :, i]

    cam = cv2.resize(cam, (456, 456))
    cam = np.maximum(cam, 0)
    heatmap = cam / np.max(cam)

    #Return to BGR [0..255] from the preprocessed image
    image = image[0, :]
    image -= np.min(image)
    image = np.minimum(image, 255)

    cam = cv2.applyColorMap(np.uint8(255*heatmap), cv2.COLORMAP_JET)
    cam = np.float32(cam) + np.float32(image)
    cam = 255 * cam / np.max(cam)
    
    del cam_model
    
    return np.uint8(cam), heatmap





def pred(hash_code, data_path, save_folder_path, weight_path):
	#set folder_path of test data
    sz = 456
    #threshold = 204
    threshold = 110
    thispath = os.path.join(data_path, hash_code)
    #datalist = [os.path.join(thispath, each) for each in os.listdir(thispath) if each.find(".png") != -1]    
    #x_train=[]
    
    model = load_model(weight_path)
    
    probs = [] 
    files = []
    for each in os.listdir(thispath):       
        if each.find(".png") != -1:                
            img = cv2.imread(thispath+"/"+each) 
            y, x = 111, 111
            if img.shape[0] > 800:
                img = img[ y:y+800, x:x+800 ]                
                img = cv2.resize(img, (sz, sz))
            else:
                img = cv2.resize(img, (sz, sz))
                                
            pre_img = preprocess_input(img[np.newaxis,...])
            
            prob = model.predict(pre_img)
    
            probs.append(prob.squeeze())
            files.append(each)
            
            predicted_class = prob.argmax(-1)
    
            cam, heatmap = grad_cam(model, pre_img, predicted_class[0], "top_activation")
            
            img_cam = cv2.addWeighted(img, 0.8, cam, 0.3, 0)
                    
            cv2.imwrite( os.path.join(save_folder_path, each) , img_cam)
            
            img = np.array(heatmap * 255, dtype = np.uint8)            
            gray = np.array(heatmap * 255, dtype = np.uint8)
            threshed = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_MEAN_C, cv2.THRESH_BINARY, 3, 0)
            ret, binary = cv2.threshold(gray,threshold,255,cv2.THRESH_BINARY)
            
            gray = np.array(heatmap * 255, dtype = np.uint8)
            threshed = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_MEAN_C, cv2.THRESH_BINARY, 3, 0)

            contours, hierarchy = cv2.findContours(binary,cv2.RETR_TREE,cv2.CHAIN_APPROX_SIMPLE)                        
            
            # save result to folder       	
            txt_save_path = save_folder_path + each[:-4] + "_cam_contour.txt"
            file = open(txt_save_path,"w")   

            for c in range(len(contours)):
                n_contour = contours[c]
                for d in range(len(n_contour)):
                    XY_Coordinates = n_contour[d]
                    #print(XY_Coordinates, XY_Coordinates[0][0], XY_Coordinates[0][1])
                    file.write("(" + str(XY_Coordinates[0][0]) + "," + str(XY_Coordinates[0][1]) +"),")                      
                file.write('\n')
            file.close()
   
    return np.array(probs), files


if __name__ == "__main__":
    #hash_code = 'simulate'
    #save_folder_path = './output/'+hash_code+'/'
    #weight_path = 'stage-2_transform_0820_resnet50_ver4'
    #data_path= './data/'
    hash_code = sys.argv[1]
    upload_folder_path = sys.argv[2]
    save_folder_path = sys.argv[3]
    weight_path = sys.argv[4]
    data_path = sys.argv[5]
    
    #python script_path hashcode upload_folder_path save_folder_path weight_path data_path;
    if not os.path.isdir(save_folder_path):
        os.mkdir(save_folder_path)

    if not os.path.isdir(data_path+hash_code+"/"):
        os.mkdir(data_path+hash_code+"/")

    #copy file into data_path, save_folder_path
    for img in os.listdir(upload_folder_path):
        shutil.copyfile(upload_folder_path+img, data_path+hash_code+"/"+img)

    #probs, filelists = pred(hash_code, data_path, save_folder_path, weight_path)
    try:
        try:    
            probs, filelists = pred(hash_code, data_path, save_folder_path, weight_path)
            print(probs)
        except Exception as inst:
            print(inst)
            print("error in pred")

        
        for count, file in enumerate(filelists): 
            # save result to folder       	
            txt_save_path = save_folder_path + file[:-4] + ".txt"
            file = open(txt_save_path,"w")   
            pred_msg = str( round(probs[count, 0], 4)*100 )[:5] + '%'          
       
            file.write(pred_msg) 
            file.close()
            count += 1
    except:
        print("Something Error...") 

