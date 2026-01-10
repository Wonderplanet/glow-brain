using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：レシート検証/購入不可の商品を買ってしまった
    /// </summary>
    public class BillingVerifyReceiptFailedException : ServerBillingException
    {
        public BillingVerifyReceiptFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
