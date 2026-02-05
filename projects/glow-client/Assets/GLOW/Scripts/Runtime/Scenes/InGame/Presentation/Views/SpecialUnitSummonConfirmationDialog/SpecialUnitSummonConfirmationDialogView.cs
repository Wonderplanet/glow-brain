using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog
{
    public class SpecialUnitSummonConfirmationDialogView : UIView
    {
        static readonly int TapStartAnimationBool_StartTap = Animator.StringToHash("StartTap");

        [SerializeField] UIText _specialAttackNameText;
        [SerializeField] UIText _specialAttackDescriptionText;
        [SerializeField] UIText _specialAttackCost;
        [SerializeField] UIObject _useSkillButton;
        [SerializeField] UIObject _tapStart;
        [SerializeField] Animator _tapStartAnimator;

        public void SetSpecialAttackName(SpecialAttackName specialAttackName)
        {
            _specialAttackNameText.SetText(specialAttackName.Value);
        }

        public void SetSpecialAttackDescription(SpecialAttackInfoDescription description)
        {
            _specialAttackDescriptionText.SetText(description.Value);
        }

        public void SetSpecialAttackCost(BattlePoint battlePoint)
        {
            _specialAttackCost.SetText(battlePoint.ToString());
        }

        /// <summary> スキル発動ボタンの表示切り替え。必殺技がコマ選択を必要とする範囲指定の場合に非表示となる </summary>
        public void SetUseSkillButtonVisible(NeedTargetSelectTypeFlag needTargetSelectTypeFlag)
        {
            _useSkillButton.Hidden = needTargetSelectTypeFlag;

            // 発動ボタンがない場合のコマ選択が必要な旨の表示
            _tapStart.Hidden = !needTargetSelectTypeFlag;
            _tapStartAnimator.SetBool(TapStartAnimationBool_StartTap, needTargetSelectTypeFlag);
        }
    }
}
