using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IIntroductionTutorialContext
    {
        UniTask DoIfPreLoadIntroductionTutorial(Func<UniTask> action);
        UniTask DoIfTutorial(Func<UniTask> action);
        void DoIfTutorial(Action action);
    }
}