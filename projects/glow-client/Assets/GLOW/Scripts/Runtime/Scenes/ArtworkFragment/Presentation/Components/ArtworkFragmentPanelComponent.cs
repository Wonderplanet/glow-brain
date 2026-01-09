using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkFragmentPanelComponent : MonoBehaviour
    {
        [SerializeField] UIImage _artworkImage;
        [SerializeField] ArtworkFragmentComponent[] _artworkFragmentComponents;

        public void Setup(ArtworkFragmentPanelViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFade(_artworkImage.Image, viewModel.AssetPath.Value);
            for (int i = 0; i < _artworkFragmentComponents.Length; i++)
            {
                var fragment =
                    viewModel.ArtworkFragmentViewModelsComponents.FirstOrDefault(component => component.PositionNum.Value == i + 1);

                if (fragment == null)
                {
                    _artworkFragmentComponents[i].gameObject.SetActive(false);
                    continue;
                }

                _artworkFragmentComponents[i].gameObject.SetActive(true);
                _artworkFragmentComponents[i].Setup(fragment);
            }
        }

        public ArtworkFragmentComponent GetPiece(int index)
        {
            return _artworkFragmentComponents[index];
        }
    }
}
