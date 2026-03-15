using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：レシート検証/不正レシート
    /// </summary>
    public class BillingVerifyReceiptInvalidReceiptException : ServerBillingException
    {
        public BillingVerifyReceiptInvalidReceiptException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
