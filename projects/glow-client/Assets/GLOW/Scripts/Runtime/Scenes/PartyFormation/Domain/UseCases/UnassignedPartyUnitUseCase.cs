using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class UnassignedPartyUnitUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }

        public bool IsValidUnassignedUnit(PartyNo partyNo, UserDataId userUnitId)
        {
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var partyUnits = ApplyUnassignedUnitList(party, userUnitId);

            return partyUnits.Any(unitId => !unitId.IsEmpty());
        }

        public void UnassignedUnit(PartyNo partyNo, UserDataId userUnitId)
        {
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var partyUnits = ApplyUnassignedUnitList(party, userUnitId);

            partyUnits.Sort((a, b) => a == UserDataId.Empty ? 1 : b == UserDataId.Empty ? -1 : 0);

            PartyCacheRepository.UpdateParty(party.PartyNo, party.PartyName, partyUnits);
        }

        List<UserDataId> ApplyUnassignedUnitList(UserPartyCacheModel party, UserDataId userUnitId)
        {
            var partyUnits = party.GetUnitList().ToList();
            if (partyUnits.All(id => id != userUnitId)) return partyUnits;

            for(var i = 0 ; i < partyUnits.Count; i++)
            {
                if (partyUnits[i] != userUnitId) continue;
                partyUnits[i] = UserDataId.Empty;
                break;
            }

            return partyUnits;
        }
    }
}
