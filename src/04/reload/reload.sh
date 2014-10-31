
echo "Reloading..."
cmd=$(pidof reload_master)

kill -USR1 "$cmd"
echo "Reloaded"