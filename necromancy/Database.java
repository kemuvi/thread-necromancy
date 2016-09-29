/*
 * Decompiled with CFR 0_115.
 */
package necromancy;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class Database {
    private static final String DBSERVER = "";
    private static final String DBNAME = "";
    private static final String DBUSER = "";
    private static final String DBPASS = "";
    private Connection connection = DriverManager.getConnection(String.format("jdbc:mysql://%s/%s?user=%s&password=%s", "", "", "", ""));

    public /* varargs */ ResultSet prepareAndExecuteQuery(String query, String ... vars) {
        ResultSet res = null;
        PreparedStatement stmt = null;
        try {
            stmt = this.connection.prepareStatement(query);
            int i = 0;
            while (i < vars.length) {
                stmt.setString(i + 1, vars[i]);
                ++i;
            }
            res = stmt.executeQuery();
        }
        catch (SQLException e) {
            e.printStackTrace();
        }
        return res;
    }

    public /* varargs */ void prepareAndExecuteUpdate(String query, String ... vars) {
        PreparedStatement stmt = null;
        try {
            stmt = this.connection.prepareStatement(query);
            int i = 0;
            while (i < vars.length) {
                stmt.setString(i + 1, vars[i]);
                ++i;
            }
            stmt.executeUpdate();
        }
        catch (SQLException e) {
            e.printStackTrace();
        }
    }
}

