const { join, resolve } = require('path');

module.exports = () => {
    return {
        resolve: {
            alias: {
                '@SellingItems': resolve(
                    join(__dirname, '..', 'src', 'Resources', 'app', 'administration', 'src')
                )
            }
        }
    };
};