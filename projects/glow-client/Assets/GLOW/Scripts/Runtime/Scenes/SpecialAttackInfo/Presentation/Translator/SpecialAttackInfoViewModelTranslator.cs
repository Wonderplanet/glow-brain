using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Translator
{
    public class SpecialAttackInfoViewModelTranslator
    {
        public static SpecialAttackInfoViewModel ToSpecialAttackInfoModel(SpecialAttackInfoModel infoModel)
        {
            var viewModel = new SpecialAttackInfoViewModel(
                infoModel.Name,
                infoModel.Description,
                infoModel.CoolTime,
                ToSpecialAttackInfoRankViewModelList(infoModel.RankModelList));

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
