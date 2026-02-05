using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class BoxGachaStockNotEnoughException : WrappedServerErrorException
    {
        public BoxGachaStockNotEnoughException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}