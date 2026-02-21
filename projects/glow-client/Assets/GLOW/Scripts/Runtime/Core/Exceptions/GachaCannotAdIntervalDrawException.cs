using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaCannotAdIntervalDrawException : DataInconsistencyServerErrorException
    {
        public GachaCannotAdIntervalDrawException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
