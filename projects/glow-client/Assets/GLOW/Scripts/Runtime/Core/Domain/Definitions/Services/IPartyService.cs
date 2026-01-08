using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IPartyService
    {
        UniTask<PartySaveResultModel> Save(CancellationToken cancellationToken, PartyNo no, PartyName name, IReadOnlyList<UserDataId> partyUnitIds);
        UniTask<PartySaveResultModel> Save(CancellationToken cancellationToken, List<UserPartyModel> userPartyModels);
    }
}
