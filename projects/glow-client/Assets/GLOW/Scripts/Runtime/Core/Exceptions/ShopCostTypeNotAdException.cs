using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopCostTypeNotAdException : WrappedServerErrorException
    {
        public ShopCostTypeNotAdException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
