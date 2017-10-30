# ams_leaderboard
Leaderboard for Automobilista.

This a permanent leaderboard to use with the Automobilista Simulator.

# done: 
- extract_data_cron.php - This is the file that read the results in the xml/ folder and translates them into a mysql database. As the name implied it is recommended to put this file in a cron job and the xml folder sync'd to your server. 
- index.php - This is the file that read the mysql database and shows the leaderboard. 

# todo:
- Proper install script. 
- Plugin to avoid the xml and upload the data straight from Automobilista.

# other:
This software is still under development. 
Use at your own risk.


