using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public class HomeStageInfoEnemyCharacterViewModelTranslator
    {
        public static IReadOnlyList<HomeStageInfoEnemyCharacterViewModel> ToHomeStageInfoEnemyCharacterViewModel(IReadOnlyList<HomeStageInfoEnemyUseCaseModel> useCaseModelList)
        {
            return useCaseModelList
                .Select(useCaseModel => new HomeStageInfoEnemyCharacterViewModel(
                    EnemyCharacterIconAssetPath.FromAssetKey(useCaseModel.EnemyIconAssetKey),
                    useCaseModel.EnemyName,
                    useCaseModel.EnemyColor,
                    useCaseModel.EnemyUnitRoleType,
                    useCaseModel.EnemyUnitKind,
                    useCaseModel.SortOrder))
                .OrderByDescending(useCaseModel => useCaseModel.CharacterUnitKind)
                .ThenBy(useCaseModel => useCaseModel.SortOrder.Value)
                .ToList();
        }
    }
}