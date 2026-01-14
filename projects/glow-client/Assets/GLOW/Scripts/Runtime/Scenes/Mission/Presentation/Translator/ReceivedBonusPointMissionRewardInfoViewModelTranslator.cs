using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Mission.Domain.Model;
using GLOW.Scenes.Mission.Presentation.ViewModel;

namespace GLOW.Scenes.Mission.Presentation.Translator
{
    public class ReceivedBonusPointMissionRewardInfoViewModelTranslator
    {
        public static ReceivedBonusPointMissionRewardInfoViewModel ToViewModel(
            ReceivedBonusPointMissionRewardInfoModel model)
        {
            return new ReceivedBonusPointMissionRewardInfoViewModel(
                model.BeforeBonusPoint,
                model.AfterBonusPoint,
                model.MaxBonusPoint,
                model.CommonReceiveResourceModels
                    .Select(m =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList(),
                model.ReceivedRewardBonusPoints);
        }
    }
}
