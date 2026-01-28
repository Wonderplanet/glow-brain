using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceSpecialAttackDetailComponent : UIObject
    {
        [SerializeField] UIText _name;
        [SerializeField] UIText _description;
        [SerializeField] UIObject _coolTimeArea;
        [SerializeField] UIText _initialCoolTime;
        [SerializeField] UIText _coolTime;

        public void Setup(UnitEnhanceSpecialAttackViewModel viewModel)
        {
            _name.SetText(viewModel.Name.Value);
            _description.SetText(viewModel.Description.Value);

            // specialの場合、クールタイム表示を非表示にする
            if (viewModel.RoleType == CharacterUnitRoleType.Special)
            {
                _coolTimeArea.IsVisible = false;
            }
            else
            {
                _coolTimeArea.IsVisible = true;
                _initialCoolTime.SetText(ZString.Format("1回目：{0:F1}秒", viewModel.InitialCoolTime.Value.ToSeconds()));
                _coolTime.SetText(ZString.Format("2回目以降：{0:F1}秒", viewModel.CoolTime.Value.ToSeconds()));
            }
        }
    }
}
