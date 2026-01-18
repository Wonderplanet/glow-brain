using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PartyFormation.Domain.Models;

namespace GLOW.Core.Domain.Models
{
    public record UserPartyModel(
        PartyNo PartyNo,
        PartyName PartyName,
        UserDataId Unit1,
        UserDataId Unit2,
        UserDataId Unit3,
        UserDataId Unit4,
        UserDataId Unit5,
        UserDataId Unit6,
        UserDataId Unit7,
        UserDataId Unit8,
        UserDataId Unit9,
        UserDataId Unit10)
    {
        public static UserPartyModel Empty { get; } = new (
            PartyNo.Empty,
            PartyName.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty,
            UserDataId.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public IReadOnlyList<UserDataId> GetUnitList()
        {
            return new List<UserDataId>
            {
                Unit1,
                Unit2,
                Unit3,
                Unit4,
                Unit5,
                Unit6,
                Unit7,
                Unit8,
                Unit9,
                Unit10
            };
        }
    }
}
