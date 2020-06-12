from locust import HttpUser, TaskSet, task
import json
import time
import random
import string
import datetime
import os

expected_time = 0.3

def broadcast_partner(self):
   partnerId = random.randint(1,8000000)
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


class WebsiteUser(HttpUser):
    tasks = {broadcast_partner: 1}
    min_wait = 5000
    max_wait = 9000
