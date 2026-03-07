using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Scenes.SpecialAttackInfo.Domain.Model;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Translator
{
    public class SpecialAttackInfoViewModelTranslator
    {
        public static SpecialAttackInfoViewModel ToSpecialAttackInfoModel(SpecialAttackInfoUseCaseModel model)
        {
            var viewModel = new SpecialAttackInfoViewModel(
                model.SpecialAttackInfoModel.Name,
                model.SpecialAttackInfoModel.Description,
                model.SpecialAttackInfoModel.CoolTime,
                model.CharacterName,
                ToSpecialAttackInfoRankViewModelList(model.SpecialAttackInfoModel.RankModelList));

            return viewModel;
        }

        static IReadOnlyList<SpecialAttackInfoGradeViewModel> ToSpecialAttackInfoRankViewModelList(IReadOnlyList<SpecialAttackInfoGradeModel> rankModelList)
        {
            var rankViewModelList = rankModelList
                .Select(rankModel =>
                    new SpecialAttackInfoGradeViewModel(
                        rankModel.UnitGrade,
                        rankModel.GradeDescription)).ToList();

            return rankViewModelList;
        }
    }
}
