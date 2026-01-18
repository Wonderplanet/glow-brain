using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：AppStoreからの応答ステータスがOKではない(AppStoreのみ)
    /// </summary>
    public class BillingAppstoreResponseStatusNotOkException : ServerBillingException
    {
        public BillingAppstoreResponseStatusNotOkException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
