using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkFragmentThumbnailComponent : MonoBehaviour
    {
        [SerializeField] UIImage _artworkImage;
        [SerializeField] GameObject[] _artworkFragmentComponents;
        [SerializeField] UIText _unlockNumText;

        public void Setup(ArtworkFragmentPanelViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFade(_artworkImage.Image, viewModel.AssetPath.Value);

            for (int i = 0; i < _artworkFragmentComponents.Length; i++)
            {
                // 原画が完成している場合は全て開放状態にする
                if (viewModel.IsCompleted)
                {
                    _artworkFragmentComponents[i].gameObject.SetActive(false);
                    continue;
                }

                // フラグメントが1つもない場合は全てロック状態にする
                if (viewModel.IsAllLocked())
                {
                    _artworkFragmentComponents[i].gameObject.SetActive(true);
                    continue;
                }

                var fragment =
                    viewModel.ArtworkFragmentViewModelsComponents.FirstOrDefault(component => component.PositionNum.Value == i + 1);

                if (fragment == null)
                {
                    _artworkFragmentComponents[i].gameObject.SetActive(false);
                    continue;
                }

                _artworkFragmentComponents[i].gameObject.SetActive(!fragment.IsUnlock);
            }

            var all = viewModel.ArtworkFragmentViewModelsComponents.Count;
            var unlock = viewModel.ArtworkFragmentViewModelsComponents.Count(fragment => fragment.IsUnlock);
            _unlockNumText.SetText($"{unlock}/{all}");
            _unlockNumText.gameObject.SetActive(all != unlock);
        }
    }
}
