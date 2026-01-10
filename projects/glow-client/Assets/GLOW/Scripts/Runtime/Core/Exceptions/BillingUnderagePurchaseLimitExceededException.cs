using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー；未成年の購入限度額超過エラー
    /// </summary>
    public class BillingUnderagePurchaseLimitExceededException : ServerBillingException
    {
        public BillingUnderagePurchaseLimitExceededException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
