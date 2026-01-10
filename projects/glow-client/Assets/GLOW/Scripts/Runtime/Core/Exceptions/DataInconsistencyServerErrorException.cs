using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// データ不整合によるサーバーエラーを表す例外クラス
    /// </summary>
    public class DataInconsistencyServerErrorException : WrappedServerErrorException
    {
        public DataInconsistencyServerErrorException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}