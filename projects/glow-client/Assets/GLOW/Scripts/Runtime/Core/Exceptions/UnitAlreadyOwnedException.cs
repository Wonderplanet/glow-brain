using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitAlreadyOwnedException : DataInconsistencyServerErrorException
    {
        public UnitAlreadyOwnedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
