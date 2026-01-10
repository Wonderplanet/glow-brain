using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class ChangeDeckUseCase
    {
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }

        public void ChangeDeckLayout()
        {
            //Layout更新されると、DeckCharacterComponentの親GameObjectが変化して、AnimationControllerのStateが初期化される
            //これによりBattlePoint最大のときはBooleanがもとに戻らないので、一度叩く
            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }
    }
}