namespace WPFramework.Modules.TimeCalibration
{
    public interface INtpContext
    {
        string Domain { get; }
        int Port { get; }
    }
}
