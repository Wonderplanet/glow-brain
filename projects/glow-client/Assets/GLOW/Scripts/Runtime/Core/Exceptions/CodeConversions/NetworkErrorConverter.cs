using System;
using System.Net.Sockets;
using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions.CodeConversions
{
    public static class NetworkErrorConverter
    {
        public static string ConvertToInquiryId(int errorCode)
        {
            return $"CLN-{errorCode}";
        }

        public static string ConvertToInquiryId(SocketException exception)
        {
            return ConvertToInquiryId(2);
        }

        public static string ConvertToInquiryId(NetworkException exception)
        {
            return exception switch
            {
                InternetNotReachableException => ConvertToInquiryId(1),
                NetworkTimeoutException => ConvertToInquiryId(3),
                // NOTE: 直接呼ぶ際には未定義のコードとして取り扱う
                _ => ConvertToInquiryId(0),
            };
        }

        public static bool HasTarget(Exception exception)
        {
            return exception switch
            {
                SocketException => true,
                _ => HasTarget(exception)
            };
        }

        public static bool HasTarget(NetworkException exception)
        {
            return exception switch
            {
                InternetNotReachableException => true,
                NetworkTimeoutException => true,
                _ => false
            };
        }
    }
}
