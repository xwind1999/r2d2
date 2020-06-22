import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from plotly.subplots import make_subplots

df = pd.read_csv('/builds/millenniumfalcon/r2-d2-api/tests/LoadTests/csvReports/staging_end2EndTests.py_stats.csv')
fig = make_subplots(rows=1, cols=1)

fig.add_trace(
    go.Scatter(x = df['Name'], y = df['Average Response Time'], name='Average Response Time'),
    row=1, col=1
)

fig.add_trace(
    go.Scatter(x = df['Name'], y = df['Median Response Time'], name='Median Response Time'),
    row=1, col=1
)

fig.add_trace(
    go.Scatter(x = df['Name'], y = df['Min Response Time'], name='Min Response Time'),
    row=1, col=1
)

fig.add_trace(
    go.Scatter(x = df['Name'], y = df['Max Response Time'], name='Max Response Time'),
    row=1, col=1
)

fig.add_trace(
    go.Scatter(x = df['Name'], y = df['Request Count'], name='Total Request Count'),
    row=1, col=1
)

fig.update_layout(height=1000, width=1500, xaxis_title="Type of Request",yaxis_title="Response in s",title_text="Locust Performance Results")
fig.write_html("../report/html/locustreport/result.html")
fig.show()
