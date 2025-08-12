for i in $(seq -w 0 14); do 
    if [ -d "ex$i" ]; then 
        if [ -f "ex$i/templates/bundles/TwigBundle/Exception/error405.html.twig" ]; then 
            echo "✅ ex$i : trouvé"; 
        else 
            echo "❌ ex$i : manquant"; 
        fi 
    fi 
done

