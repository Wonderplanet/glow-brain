using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using GLOW.Scenes.QuestContentTop.Presentation.ViewModel;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public static class QuestContentTopViewModelTranslator
    {
        public static QuestContentTopViewModel Translate(QuestContentTopUseCaseModel useCaseModel)
        {
            var eventSection = TranslateSectionViewModel(useCaseModel.EventSection);
            var daily = TranslateSectionViewModel(useCaseModel.DailySection);
            var endContent = TranslateSectionViewModel(useCaseModel.EndContentSection);
            var pvpSection = TranslateSectionViewModel(useCaseModel.PvPSection);
            return new QuestContentTopViewModel(
                new List<QuestContentTopSectionViewModel>() { eventSection, daily, pvpSection, endContent }
            );
        }

        static QuestContentTopSectionViewModel TranslateSectionViewModel(QuestContentTopSectionUseCaseModel model)
        {
            return new QuestContentTopSectionViewModel(
                model.Type,
                GetSortedItems(model.Items.Select(TranslateCellViewModel).ToList())
            );
        }

        static IReadOnlyList<QuestContentCellViewModel> GetSortedItems(IReadOnlyList<QuestContentCellViewModel> items)
        {
            // 表示順番を設定
            var sortList = new List<QuestContentTopElementType>()
            {
                QuestContentTopElementType.Event,
                QuestContentTopElementType.Enhance,
                QuestContentTopElementType.Pvp,
                QuestContentTopElementType.AdventBattle,
                QuestContentTopElementType.Limited,
                QuestContentTopElementType.Other,
            };
            return items.OrderBy(i => sortList.IndexOf(i.ElementType)).ToList();
        }

        static QuestContentCellViewModel TranslateCellViewModel(QuestContentTopElementUseCaseModel model)
        {
            return new QuestContentCellViewModel(
                model.ElementType,
                model.OpeningStatusModel,
                model.ChallengeCount,
                model.ChallengeType,
                model.ChallengeResetTime,
                model.RemainingTimeSpan,
                model.HasRanking,
                model.HasRankingNotification,
                model.HasBannerBadgeNotification,
                model.MstEventId,
                model.EventName,
                model.BannerAssetPath,
                model.CampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList()
            );
        }
    }
}
