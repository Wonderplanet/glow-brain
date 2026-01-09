using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：allowanceで購入不可
    /// </summary>
    public class BillingAllowanceFailedException : ServerBillingException
    {
        public BillingAllowanceFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
