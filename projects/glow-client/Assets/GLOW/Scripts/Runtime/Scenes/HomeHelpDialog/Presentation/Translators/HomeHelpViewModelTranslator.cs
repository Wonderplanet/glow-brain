using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;
using GLOW.Scenes.HomeHelpDialog.Presentation.Misc;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Translators
{
    public class HomeHelpViewModelTranslator
    {
        public static HomeHelpViewModel Translate(HomeHelpInfoList homeHelpInfoList)
        {
            var majorCells = homeHelpInfoList.MainContentInfoList
                .Select(TranslateMainContentCell)
                .ToList();

            return new HomeHelpViewModel(majorCells);
        }

        static HomeHelpMainContentCellViewModel TranslateMainContentCell(HomeHelpMainContentInfo homeHelpMainContentInfo)
        {
            var minorCells = homeHelpMainContentInfo.SubContentInfoList
                .Select(TranslateSubContentCell)
                .ToList();

            return  new HomeHelpMainContentCellViewModel(
                new HomeHelpTitle(homeHelpMainContentInfo.Header),
                minorCells);;
        }

        static HomeHelpSubContentCellViewModel TranslateSubContentCell(HomeHelpSubContentInfo homeHelpSubContentInfo)
        {
            return new HomeHelpSubContentCellViewModel(
                new HomeHelpTitle(homeHelpSubContentInfo.Header),
                TranslateArticle(homeHelpSubContentInfo.Article)
                );
        }

        static IReadOnlyList<HomeHelpArticleViewModel> TranslateArticle(string article)
        {
            return HomeHelpArticleParser.Parse(article);
        }
    }
}
