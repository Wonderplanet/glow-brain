using System;
using System.Collections.Generic;
using GLOW.Scenes.HomeHelpDialog.Constants;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Misc
{
    public static class HomeHelpArticleParser
    {
        public static List<HomeHelpArticleViewModel> Parse(string text)
        {
            var result = new List<HomeHelpArticleViewModel>();
            var lines = text.Split(new[] { '\n' });

            foreach (var line in lines)
            {
                if (line.StartsWith("!"))
                {
                    result.Add(new HomeHelpArticleViewModel(HomeHelpArticleType.Image, line.TrimStart('!')));
                }
                else
                {
                    result.Add(new HomeHelpArticleViewModel(HomeHelpArticleType.Text, line));
                }
            }

            return result;
        }
    }
}
