using GLOW.Core.Domain.Models;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Translators
{
    public static class EnemyIconViewModelTranslator
    {
        public static EnemySmallIconViewModel Translate(EnemySmallIconModel model)
        {
            return new EnemySmallIconViewModel(model.AssetPath);
        }
    }
}
