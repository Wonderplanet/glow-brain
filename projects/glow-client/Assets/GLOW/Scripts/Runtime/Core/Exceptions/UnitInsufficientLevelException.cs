using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitInsufficientLevelException : DataInconsistencyServerErrorException
    {
        public UnitInsufficientLevelException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
