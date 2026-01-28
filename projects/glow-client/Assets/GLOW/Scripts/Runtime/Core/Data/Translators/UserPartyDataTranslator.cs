using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserPartyDataTranslator
    {
        public static UserPartyModel TranslateToModel(UsrPartyData data)
        {
            return new UserPartyModel(
                new PartyNo(data.PartyNo),
                new PartyName(data.PartyName),
                UserDataId.CreateOrEmpty(data.UsrUnitId1),
                UserDataId.CreateOrEmpty(data.UsrUnitId2),
                UserDataId.CreateOrEmpty(data.UsrUnitId3),
                UserDataId.CreateOrEmpty(data.UsrUnitId4),
                UserDataId.CreateOrEmpty(data.UsrUnitId5),
                UserDataId.CreateOrEmpty(data.UsrUnitId6),
                UserDataId.CreateOrEmpty(data.UsrUnitId7),
                UserDataId.CreateOrEmpty(data.UsrUnitId8),
                UserDataId.CreateOrEmpty(data.UsrUnitId9),
                UserDataId.CreateOrEmpty(data.UsrUnitId10)
                );
        }
    }
}
