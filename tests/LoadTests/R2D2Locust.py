from locust import HttpLocust, TaskSet

expected_time = 3.0

def get_sample(self):
    response = self.client.get("api/doc.json")
    assert response.elapsed.total_seconds() < expected_time, "Request took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
    print ("response code", response.status_code)
    assert response.status_code == 200

class R2D2(TaskSet):
    tasks = { get_sample: 1 }

class WebsiteUser(HttpLocust):
    task_set = R2D2
    min_wait = 5000
    max_wait = 9000
