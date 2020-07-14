from locust import HttpUser, TaskSet, task
import json
import time
import random
import string
import datetime
import os

expected_time = 0.3
partnerId = random.randint(1,8000000)
boxProductId = random.randint(1,800000)
experienceProductId = random.randint(1,800000)

def broadcast_partner(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/partner",data= json.dumps({
   "id": partnerId,
   "status" : "active",
   "currencyCode" : "EUR",
   "isChannelManagerEnabled" : False
   }),
   headers=headers,
   name = "Broadcast Partner to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Partner took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_box(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product",data= json.dumps({
   "id": boxProductId,
   "name": "Load Test Box",
   "status": "inactive",
   "currencyCode": "EUR",
   "isSellable" : False,
   "isReservable" : False,
   "type": "mev",
   "partner":{
     "id":partnerId
    },
   "sellableBrand" : {
     "code":"SBX"
    },
   "sellableCountry":{
     "code":"FR"
    },
   "listPrice": {
     "currencyCode": "EUR",
     "amount" : 100
     }
   }),
   headers=headers,
   name = "Broadcast Product Box to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Product Type Box took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_experience(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product",data= json.dumps({
   "id": experienceProductId,
   "name": "Load Test Experience",
   "status": "inactive",
   "currencyCode": "EUR",
   "isSellable" : False,
   "isReservable" : False,
   "type": "experience",
   "partner":{
     "id":partnerId
    },
   "sellableBrand" : {
     "code":"SBX"
    },
   "sellableCountry":{
     "code":"FR"
    },
   "listPrice": {
     "currencyCode": "EUR",
     "amount" : 100
     }
   }),
   headers=headers,
   name = "Broadcast Product Experience to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Product Experience type took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_relationship(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product-relationship",data= json.dumps({
   "parentProduct": boxProductId,
   "childProduct" : experienceProductId,
   "relationshipType" : "Box-Experience",
   "isEnabled" : False
   }),
   headers=headers,
   name = "Broadcast Box Experience Relationship to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Box Experience took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_price_relationship(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/price-information",data= json.dumps({
   "product": {
     "id":experienceProductId
   },
   "averageValue" : {
     "amount" : 100,
     "currencyCode" : "EUR"
   },
   "averageCommissionType" : "percentage",
   "averageCommission" : 10
   }),
   headers=headers,
   name = "Broadcast Price Information to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Price Information took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

class WebsiteUser(HttpUser):
    tasks = {broadcast_partner: 1,broadcast_product_box: 2, broadcast_product_experience: 2, broadcast_product_relationship: 1, broadcast_price_relationship: 1}
    min_wait = 5000
    max_wait = 9000
