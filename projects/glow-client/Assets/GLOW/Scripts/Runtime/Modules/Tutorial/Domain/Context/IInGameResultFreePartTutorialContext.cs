using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IInGameResultFreePartTutorialContext
    {
        UniTask DoIfTutorial(Func<UniTask> action);
        void DoIfTutorial(Action action);
    }
}