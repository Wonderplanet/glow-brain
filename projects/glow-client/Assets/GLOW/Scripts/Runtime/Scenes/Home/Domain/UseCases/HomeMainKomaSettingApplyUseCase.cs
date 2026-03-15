using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainKomaSettingApplyUseCase
    {
        [Inject] IHomeMainKomaSettingUserRepository UserRepository { get; }

        public void SaveUnit(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId targetMstUnitId)
        {
            if (!UserRepository.IsLoaded)
            {
                // 副作用
                UserRepository.Load();
            }

            UserRepository.SaveUnit(
                targetMstHomeMainKomaPatternId,
                targetUnitAssetSetPlaceIndex,
                targetMstUnitId
            );
        }
    }
}
