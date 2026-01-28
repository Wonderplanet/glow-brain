namespace WPFramework.Modules.Environment
{
    /// <summary>
    /// 環境データのパースを行うためのインターフェース
    /// </summary>
    public interface IEnvironmentDataParser
    {
        T Parse<T>(byte[] bytes);
        T Parse<T>(string text);
    }
}
