using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopTradeCountLimitException : DataInconsistencyServerErrorException
    {
        public ShopTradeCountLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
