using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public static class UserInGameStatusDataTranslator
    {
        public static UserInGameStatusModel ToUserInGameStatusModel(UsrInGameStatusData data)
        {
            var mstStageId = string.IsNullOrEmpty(data.TargetMstId)
                ? MasterDataId.Empty
                : new MasterDataId(data.TargetMstId);

            var inGameContentType = data.InGameContentType ?? InGameContentType.Stage;

            return new UserInGameStatusModel(
                data.IsStartedSession ? InGameSessionStartedFlag.True : InGameSessionStartedFlag.False,
                inGameContentType,
                mstStageId,
                new PartyNo(data.PartyNo),
                new ContinueCount(data.ContinueCount),
                new ContinueCount(data.ContinueAdCount));
        }

        public static UsrInGameStatusData ToUsrInGameStatusData(UserInGameStatusModel data)
        {
            return new UsrInGameStatusData()
            {
                IsStartedSession = data.IsStartedSession,
                InGameContentType = data.InGameContentType,
                TargetMstId = data.TargetMstId.Value,
                PartyNo = data.PartyNo.Value,
                ContinueCount = data.ContinueCount.Value,
                ContinueAdCount = data.ContinueAdCount.Value
            };
    }
    }
}
