using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Presentation.Translators;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public class HomeStageInfoViewModelFactory
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public HomeStageInfoViewModel ToHomeStageInfoViewModel(HomeStageInfoUseCaseModel model)
        {
            return new HomeStageInfoViewModel(
                HomeStageInfoEnemyCharacterViewModelTranslator.ToHomeStageInfoEnemyCharacterViewModel(model.EnemyCharacters),
                ToTreasurePlayerResourceIconViewModel(model.ArtworkFragmentResource),
                ToRewardPlayerResourceIconViewModel(model.PlayerResources),
                ToInGameSpecialRuleViewModel(model.InGameSpecialRule),
                ToSpeedAttackViewModel(model.SpeedAttack),
                model.InGameDescription
                );
        }

        IReadOnlyList<PlayerResourceIconViewModel> ToTreasurePlayerResourceIconViewModel(IReadOnlyList<HomeStageInfoArtworkFragmentUseCaseModel> playerResourceModels)
        {
            var acquiredPlayerResources = playerResourceModels
                .Select(fragment =>
                    PlayerResourceModelFactory.Create(
                        fragment.HomeStageInfoArtworkFragmentResource.ResourceType,
                        fragment.HomeStageInfoArtworkFragmentResource.ResourceId,
                        fragment.HomeStageInfoArtworkFragmentResource.ResourceAmount,
                        RewardCategory.Always,
                        fragment.AcquiredFlag)
                    )
                .ToList();

            return PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(acquiredPlayerResources);
        }

        IReadOnlyList<PlayerResourceIconViewModel> ToRewardPlayerResourceIconViewModel(IReadOnlyList<HomeStageInfoRewardUseCaseModel> playerResourceModels)
        {
            var acquiredPlayerResources = playerResourceModels
                .Select(reward =>
                    PlayerResourceModelFactory.Create(
                        reward.HomeStageInfoRewardResource.ResourceType,
                        reward.HomeStageInfoRewardResource.ResourceId,
                        reward.HomeStageInfoRewardResource.ResourceAmount,
                        reward.Category,
                        reward.AcquiredFlag))
                .OrderBy(playerResource => playerResource.RewardCategory == RewardCategory.FirstClear ? 1 : 2)
                .ThenBy(playerResource => playerResource.RewardCategory == RewardCategory.Always ? 1 : 2)
                .ThenBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value)
                .ToList();

            return PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(acquiredPlayerResources, true);
        }

        HomeStageInfoSpeedAttackViewModel ToSpeedAttackViewModel(HomeStageInfoSpeedAttackUseCaseModel speedAttackUseCaseModel)
        {
            if(speedAttackUseCaseModel.IsEmpty()) return HomeStageInfoSpeedAttackViewModel.Empty;

            var clearTimeRewards = speedAttackUseCaseModel.ClearTimeRewards
                .Select(speedAttackRewardUseCaseModel =>
                    PlayerResourceModelFactory.Create(
                        speedAttackRewardUseCaseModel.HomeStageInfoRewardResource.ResourceType,
                        speedAttackRewardUseCaseModel.HomeStageInfoRewardResource.ResourceId,
                        speedAttackRewardUseCaseModel.HomeStageInfoRewardResource.ResourceAmount,
                        RewardCategory.Always,
                        speedAttackRewardUseCaseModel.AcquiredFlag,
                        speedAttackRewardUseCaseModel.Time)
                )
                .Select(model => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model, true))
                .ToList();

            return new HomeStageInfoSpeedAttackViewModel(
                speedAttackUseCaseModel.ClearTimeMs,
                clearTimeRewards
                );
        }

        InGameSpecialRuleViewModel ToInGameSpecialRuleViewModel(InGameSpecialRuleModel model)
        {
            return InGameSpecialRuleViewModelTranslator.TranslateInGameSpecialRuleViewModel(model, InGameSpecialRuleFromUnitSelectFlag.False);
        }
    }
}
