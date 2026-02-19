using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions.CodeConversions
{
    public static class HttpErrorConverter
    {
        public static string ConvertToInquiryId(HTTPStatusCodes statusCode)
        {
            return $"CLH-{(int)statusCode}";
        }
    }
}
