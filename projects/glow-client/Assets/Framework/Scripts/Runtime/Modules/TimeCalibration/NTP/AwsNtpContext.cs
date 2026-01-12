namespace WPFramework.Modules.TimeCalibration
{
    public sealed class AwsNtpContext : INtpContext
    {
        public string Domain { get; } = "time.aws.com";
        public int Port { get; } = 123;
    }
}
