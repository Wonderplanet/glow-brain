using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainKomaSettingUseCase
    {
        [Inject] IHomeMainKomaSettingUserRepository UserRepository { get; }
        [Inject] IMstHomeKomaPatternRepository HomeMainKomaPatternRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public HomeMainKomaSettingUseCaseModel LoadAndGetHomeMainKomaSettingUseCaseModel()
        {
            if (!UserRepository.IsLoaded)
            {
                // 副作用
                UserRepository.Load();
            }

            // Repository取得
            // Modelに整形
            var model = HomeMainKomaPatternRepository.GetHomeKomaPatterns()
                .GroupJoin(
                    UserRepository.GetAll(),
                    mstModel => mstModel.Id,
                    userModel => userModel.MstKomaPatternId,
                    (mstModel, userModels) =>
                    {
                        var userModel = userModels.FirstOrDefault(UserHomeMainKomaPatternModel.CreateEmpty(mstModel.Id));
                        return CreateHomeMainKomaPatternUseCaseModel(mstModel, userModel);
                    })
                .ToList();

            return new HomeMainKomaSettingUseCaseModel(
                GetInitialSettingIndex(),
                model);
        }

        HomeMainKomaSettingIndex GetInitialSettingIndex()
        {
            var targetMstId = UserRepository.CurrentMstHomeKomaPatternId;
            var index = HomeMainKomaPatternRepository.GetHomeKomaPatterns()
                .Select(m => m.Id)
                .IndexOf(targetMstId);

            // 見つからないときEmpty返す
            if (index < 0)
            {
                return HomeMainKomaSettingIndex.Empty;
            }
            return new HomeMainKomaSettingIndex(index);
        }

        HomeMainKomaPatternUseCaseModel CreateHomeMainKomaPatternUseCaseModel(
            MstHomeKomaPatternModel mstHomeKomaPatternModel,
            UserHomeMainKomaPatternModel userModel)
        {
            return new HomeMainKomaPatternUseCaseModel(
                mstHomeKomaPatternModel.Id,
                mstHomeKomaPatternModel.Name,
                HomeMainKomaPatternAssetPath.FromAssetKey(mstHomeKomaPatternModel.AssetKey),
                CreateHomeMainKomaUnitUseCaseModel(userModel.UserHomeKomaUnitSettingModels)
            );
        }

        IReadOnlyList<HomeMainKomaUnitUseCaseModel> CreateHomeMainKomaUnitUseCaseModel(
            IReadOnlyList<UserHomeKomaUnitSettingModel> userModels)
        {
            return userModels.Join(
                MstCharacterDataRepository.GetCharacters().ToList(),
                userModel => userModel.MstUnitId,
                mstUnit => mstUnit.Id,
                (userModel, mstUnit) => new HomeMainKomaUnitUseCaseModel(
                    userModel.MstUnitId,
                    userModel.PlaceIndex,
                    HomeMainKomaUnitAssetPath.FromAssetKey(mstUnit.AssetKey)
                )
            ).ToList();
        }
    }
}
