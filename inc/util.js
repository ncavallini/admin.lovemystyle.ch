function getStatusIcon(status) {
    if(status === "OK") {
        return '<i class="fas fa-check-circle text-success"></i>';
    } else if(status === "WARN") {
        return '<i class="fas fa-triangle-exclamation text-warning"></i>';
    }
    else if(status === "ERROR") {
        return '<i class="fas fa-times-circle text-danger"></i>';
    }
}