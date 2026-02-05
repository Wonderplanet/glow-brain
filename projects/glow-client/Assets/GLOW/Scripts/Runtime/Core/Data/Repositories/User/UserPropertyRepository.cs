using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.User;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public sealed class UserPropertyRepository : IUserPropertyRepository
    {
        [Inject] IUserPropertyDataStore DataStore { get; }

        async UniTask IUserPropertyRepository.Load(CancellationToken cancellationToken)
        {
            await DataStore.Load(cancellationToken);
        }

        void IUserPropertyRepository.Save(UserPropertyModel userPropertyModel)
        {
            DataStore.Save(new UserPropertyData(
                userPropertyModel.IsBgmMute,
                userPropertyModel.IsSeMute,
                userPropertyModel.SpecialAttackCutInPlayType,
                userPropertyModel.IsPushOff,
                userPropertyModel.IsTwoRowDeck,
                userPropertyModel.IsDamageDisplay));
        }

        UserPropertyModel IUserPropertyRepository.Get()
        {
            var userPropertyData = DataStore.Get();
            return new UserPropertyModel(
                new BgmMuteFlag(userPropertyData.IsBgmMute),
                new SeMuteFlag(userPropertyData.IsSeMute),
                userPropertyData.SpecialAttackCutInPlayType,
                new PushOffFlag(userPropertyData.IsPushOff),
                new TwoRowDeckModeFlag(userPropertyData.IsTwoRowDeck),
                new DamageDisplayFlag(userPropertyData.IsDamageDisplay));
        }
    }
}
