using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Constants;
using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public class AgreementRequestHeaderAssignor : IAgreementRequestHeaderAssignor
    {
        void IAgreementRequestHeaderAssignor.SetRequestHeaders(ServerApi context)
        {
            string bearerToken = Credentials.AgreementBearerToken;

            var headers =
                new Dictionary<string, string>(context.AdditionalRequestHeaders ?? new Dictionary<string, string>());
            headers[RequestHeader.Agreement.Authorization] = ZString.Format("Bearer {0}", bearerToken);

            context.AdditionalRequestHeaders = headers;
        }
    }
}
