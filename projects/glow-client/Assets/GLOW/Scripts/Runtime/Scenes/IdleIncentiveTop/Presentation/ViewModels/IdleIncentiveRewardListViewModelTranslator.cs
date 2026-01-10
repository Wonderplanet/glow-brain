using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels
{
    public static class IdleIncentiveRewardListViewModelTranslator
    {
        public static IdleIncentiveRewardListViewModel TranslateRewardList(IdleIncentiveRewardListModel model)
        {
            var rewards = model.Rewards
                .Select(TranslateRewardListCell)
                .ToList();
            return new IdleIncentiveRewardListViewModel(rewards);
        }

        static IdleIncentiveRewardListCellViewModel TranslateRewardListCell(IdleIncentiveRewardListCellModel model)
        {
            var resourceIconViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.Reward);
            return new IdleIncentiveRewardListCellViewModel(resourceIconViewModel);
        }
    }
}
