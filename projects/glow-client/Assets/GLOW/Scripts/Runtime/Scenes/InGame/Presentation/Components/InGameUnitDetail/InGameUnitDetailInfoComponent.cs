using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailInfoComponent : UIObject
    {
        [SerializeField] CharacterLongIconComponent _unitIcon;
        [SerializeField] IconRarityImage _rarityIcon;
        [SerializeField] CharacterBonusIconComponent _bonusIcon;
        [SerializeField] UIText _effectEmptyText;
        [SerializeField] UIImage[] _effectIconList;

        public void Setup(InGameUnitDetailInfoViewModel viewModel, BattleStateEffectViewManager battleStateEffectViewManager)
        {
            _unitIcon.Setup(viewModel.IconViewModel);
            
            // インゲームキャラ詳細では上部の名前表示の横にレアリティを表示するため、ここでは非表示にする
            _rarityIcon.IsVisible = false;
            
            SetupEffectIcon(viewModel.StateEffectTypeList, battleStateEffectViewManager);
            _effectEmptyText.Hidden = viewModel.StateEffectTypeList.Count != 0;
            _bonusIcon.Hidden = viewModel.BonusPercentage.IsEmpty();
            _bonusIcon.Setup(viewModel.BonusPercentage);
        }

        void SetupEffectIcon(IReadOnlyList<StateEffectType> effectList, BattleStateEffectViewManager battleStateEffectViewManager)
        {
            int count = 0;
            foreach(var icon in _effectIconList)
            {
                if (effectList.Count <= count)
                {
                    icon.Hidden = true;
                    continue;
                }

                icon.Sprite = battleStateEffectViewManager.GetStateEffectViewData(effectList[count])?.Icon;

                count++;
            }
        }
    }
}
