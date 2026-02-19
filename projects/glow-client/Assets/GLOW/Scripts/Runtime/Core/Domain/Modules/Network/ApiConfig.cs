namespace GLOW.Core.Domain.Modules.Network
{
    public static class ApiConfig
    {
        // NOTE: コネクションタイムアウトの時間を指定（秒）
        //       https://docs.google.com/spreadsheets/d/1JTpr8t0spRWCaF_fUlwcu1a7MLbRimaQ-LgNnLxlSvM/edit#gid=0
        public const int ConnectionTimeoutSeconds = 20;
        // NOTE: リクエストタイムアウトの時間を指定（秒）
        //       https://docs.google.com/spreadsheets/d/1JTpr8t0spRWCaF_fUlwcu1a7MLbRimaQ-LgNnLxlSvM/edit#gid=0
        public const int RequestTimeoutSeconds = 30;
        public const int AgreementRequestTimeoutSeconds = 3;
    }
}
