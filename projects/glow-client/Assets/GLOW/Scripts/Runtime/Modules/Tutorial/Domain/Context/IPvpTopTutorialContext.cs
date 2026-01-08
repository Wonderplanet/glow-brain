using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IPvpTopTutorialContext
    {
        UniTask<bool> DoIfTutorial(Func<UniTask> action);
    }
}