using System;
using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class InvalidPartyUnitCountException : ServerErrorException
    {
        public InvalidPartyUnitCountException(HTTPStatusCodes statusCode, Exception innerException)
            : base(statusCode, innerException)
        {
        }
    }
}
