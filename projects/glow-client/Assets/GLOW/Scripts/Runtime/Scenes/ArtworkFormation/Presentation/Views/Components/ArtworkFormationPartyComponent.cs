using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public class ArtworkFormationPartyComponent : UIObject
    {
        [SerializeField] List<ArtworkFormationPartyCell> _partyCells;
        [SerializeField] ChildScaler _childScaler;

        public IArtworkFormationPartyComponentDelegate Delegate { get; set; }
        
        bool _hasPlayedInitialAnimation;
        
        public void SetUp(ArtworkFormationPartyViewModel viewModel)
        {
            for (int i = 0; i < _partyCells.Count; i++)
            {
                var cell = _partyCells[i];
                
                if (i < viewModel.CellViewModels.Count)
                {
                    var cellViewModel = viewModel.CellViewModels[i];

                    if (cellViewModel.IsEmpty())
                    {
                        // 空の場合は画像を解放して非表示
                        cell.Clear();
                        continue;
                    }
                    
                    cell.IsVisible = true;
                    cell.SetUp(cellViewModel);
                    
                    // セルタップ時の処理を登録
                    cell.SetOnClickListener(() =>
                    {
                        if (!cellViewModel.IsEmpty())
                        {
                            Delegate?.OnPartyCellTapped(cellViewModel.MstArtworkId);
                        }
                    });
                }
                else
                {
                    // 範囲外のセルは画像を解放して非表示
                    cell.Clear();
                }
            }
            
            // 初回表示時のみアニメーションを再生
            if (_childScaler != null && !_hasPlayedInitialAnimation)
            {
                _hasPlayedInitialAnimation = true;
                StartCoroutine(PlayAnimationAfterSetUp());
            }
        }
        
        System.Collections.IEnumerator PlayAnimationAfterSetUp()
        {
            // 1フレーム待ってセルの状態が確定するのを待つ
            yield return null;
            _childScaler.Play();
        }
    }
}

