using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class EnemySmallIconComponent : UIObject
    {
        [SerializeField] UIImage _enemyImage;

        public void Setup(EnemySmallIconViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _enemyImage.Image,
                viewModel.AssetPath.Value);
        }
    }
}
