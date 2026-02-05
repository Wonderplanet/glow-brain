using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：その他、想定していないエラー
    /// </summary>
    public class BillingUnknownErrorException : ServerBillingException
    {
        public BillingUnknownErrorException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
