using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：allowanceとMstStoreProductが不整合
    /// </summary>
    public class BillingAllowanceAndMstStoreProductNotMatchException : ServerBillingException
    {
        public BillingAllowanceAndMstStoreProductNotMatchException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
