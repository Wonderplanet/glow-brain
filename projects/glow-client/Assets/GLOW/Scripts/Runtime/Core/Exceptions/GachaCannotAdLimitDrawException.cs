using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaCannotAdLimitDrawException : DataInconsistencyServerErrorException
    {
        public GachaCannotAdLimitDrawException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
