using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesCollectionListComponent : UIComponent
    {
        [Header("原画")]
        [SerializeField] GameObject _artworkSectionHeader;
        [SerializeField] GameObject _artworkList;
        [SerializeField] EncyclopediaSeriesArtworkListCell _artworkCellPrefab;
        [SerializeField] UIText _artworkHeaderText;
        [SerializeField] ChildScaler _artworkListChildScaler;

        [Header("エンブレム")]
        [SerializeField] GameObject _emblemSectionHeader;
        [SerializeField] GameObject _emblemList;
        [SerializeField] EncyclopediaSeriesEmblemListCell _emblemCellPrefab;
        [SerializeField] UIText _emblemHeaderText;
        [SerializeField] ChildScaler _emblemListChildScaler;

        List<EncyclopediaSeriesArtworkListCell> _artworkCells = new ();
        List<EncyclopediaSeriesEmblemListCell> _emblemCells = new ();

        public void Setup(EncyclopediaSeriesCollectionListViewModel viewModel,
            Action<MasterDataId> onSelectArtworkAction,
            Action<MasterDataId> onSelectEmblemAction)
        {
            CreateArtworkList(viewModel.ArtworkList, onSelectArtworkAction);
            CreateEmblemList(viewModel.EmblemList, onSelectEmblemAction);
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _artworkListChildScaler.Play();
            _emblemListChildScaler.Play();
        }

        void CreateArtworkList(IReadOnlyList<EncyclopediaArtworkListCellViewModel> viewModels, Action<MasterDataId> onSelectArtworkAction)
        {
            var artworkMax = viewModels.Count;
            var artworkUnlockCount = artworkMax == 0 ?
                0 :
                viewModels.Count(vm => vm.IsUnlocked.Value);
            _artworkHeaderText.SetText($"{artworkUnlockCount}/{artworkMax}");
            _artworkSectionHeader.SetActive(artworkMax > 0);
            _artworkList.SetActive(artworkMax > 0);

            if (_artworkCells.Count < artworkMax)
            {
                for (int i = _artworkCells.Count; i < artworkMax; ++i)
                {
                     var cell = Instantiate(_artworkCellPrefab, _artworkList.transform);
                    _artworkCells.Add(cell);
                }
            }

            for (int i = 0; i < artworkMax; ++i)
            {
                _artworkCells[i].Setup(viewModels[i], onSelectArtworkAction);
                _artworkCells[i].gameObject.SetActive(true);
            }

            for (int i = artworkMax; i < _artworkCells.Count; ++i)
            {
                _artworkCells[i].gameObject.SetActive(false);
            }
        }

        void CreateEmblemList(IReadOnlyList<EncyclopediaEmblemListCellViewModel> viewModels, Action<MasterDataId> onSelectEmblemAction)
        {
            var emblemMax = viewModels.Count;
            var emblemUnlockCount = emblemMax == 0 ?
                0 :
                viewModels.Count(vm => vm.IsUnlocked.Value);
            _emblemHeaderText.SetText($"{emblemUnlockCount}/{emblemMax}");
            _emblemSectionHeader.SetActive(emblemMax > 0);
            _emblemList.SetActive(emblemMax > 0);

            if (_emblemCells.Count < emblemMax)
            {
                for (int i = _emblemCells.Count; i < emblemMax; ++i)
                {
                    var cell = Instantiate(_emblemCellPrefab, _emblemList.transform);
                    _emblemCells.Add(cell);
                }
            }

            for (int i = 0; i < emblemMax; ++i)
            {
                _emblemCells[i].Setup(viewModels[i], onSelectEmblemAction);
                _emblemCells[i].gameObject.SetActive(true);
            }

            for (int i = emblemMax; i < _emblemCells.Count; ++i)
            {
                _emblemCells[i].gameObject.SetActive(false);
            }
        }
    }
}
