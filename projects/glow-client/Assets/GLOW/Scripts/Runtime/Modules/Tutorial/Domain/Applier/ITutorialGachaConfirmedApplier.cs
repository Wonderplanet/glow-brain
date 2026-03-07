using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;

namespace GLOW.Modules.Tutorial.Domain.Applier
{
    public interface ITutorialGachaConfirmedApplier
    {
        UniTask ApplyPartyAndAvatarIfNeeds(CancellationToken cancellationToken);
        
        UniTask<(PartySaveResultModel, UserChangeAvatarResultModel)> ApplyPartyAndAvatar(
            CancellationToken cancellationToken,
            UserUnitModel urUnit);
        
        UserUnitModel GetUrUnitIdOrFirst(IReadOnlyList<UserUnitModel> resultModels);
        
        List<UserPartyModel> CreatePartyModels(UserUnitModel userUnitModel);
    }
}


