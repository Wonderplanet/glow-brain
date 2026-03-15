using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：allowanceとOprProductが不整合
    /// </summary>
    public class BillingAllowanceAndOprProductNotMatchException : ServerBillingException
    {
        public BillingAllowanceAndOprProductNotMatchException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
