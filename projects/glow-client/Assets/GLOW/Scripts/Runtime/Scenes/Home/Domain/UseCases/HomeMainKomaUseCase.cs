using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainKomaUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstHomeKomaPatternRepository MstHomeKomaPatternRepository { get; }
        [Inject] IHomeMainKomaSettingUserRepository HomeMainKomaSettingUserRepository { get; }

        public HomeMainKomaUseCaseModel GetHomeMainKomaUseCaseModel()
        {
            return new HomeMainKomaUseCaseModel(
                GetAssetPath(),
                GetHomeMainKomaUnitUseCaseModels()
                );
        }

        HomeMainKomaPatternAssetPath GetAssetPath()
        {
            var currentMstHomeKomaPatternId = HomeMainKomaSettingUserRepository.CurrentMstHomeKomaPatternId;
            if (currentMstHomeKomaPatternId.IsEmpty())
            {
                return HomeMainKomaPatternAssetPath.FromAssetKey(
                    MstHomeKomaPatternRepository.GetHomeKomaPatterns().First().AssetKey);
            }

            var assetKey = MstHomeKomaPatternRepository.GetHomeKomaPatterns()
                .First(x => x.Id == currentMstHomeKomaPatternId).AssetKey;
            return HomeMainKomaPatternAssetPath.FromAssetKey(assetKey);
        }

        IReadOnlyList<HomeMainKomaUnitUseCaseModel> GetHomeMainKomaUnitUseCaseModels()
        {
            var currentMstCharacterIds = HomeMainKomaSettingUserRepository.GetCurrentHomeKomaMstUnitIds();

            return currentMstCharacterIds
                .Select(indexAndMstUnitId =>
                {
                    return CreateHomeMainKomaUnitUseCaseModel(indexAndMstUnitId.Key, indexAndMstUnitId.Value);
                })
                .ToList();
        }

        HomeMainKomaUnitUseCaseModel CreateHomeMainKomaUnitUseCaseModel(
            HomeMainKomaUnitAssetSetPlaceIndex index,
            MasterDataId mstUnitId)
        {
            if (mstUnitId.IsEmpty())
            {
                return HomeMainKomaUnitUseCaseModel.CreateEmpty(index);
            }

            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            return new HomeMainKomaUnitUseCaseModel(
                mstUnit.Id,
                index,
                HomeMainKomaUnitAssetPath.FromAssetKey(mstUnit.AssetKey)
            );
        }

    }
}
